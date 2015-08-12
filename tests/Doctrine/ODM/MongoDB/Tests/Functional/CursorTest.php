<?php

namespace Doctrine\ODM\MongoDB\Tests\Functional;

use Documents\User;

class CursorTest extends \Doctrine\ODM\MongoDB\Tests\BaseTest
{
    public function testCursorShouldHydrateResults()
    {
        $user = new User();
        $user->setUsername('foo');

        $this->dm->persist($user);
        $this->dm->flush();

        $cursor = $this->uow->getDocumentPersister('Documents\User')->loadAll();

        $cursor->next();
        $this->assertSame($user, $cursor->current());

        $cursor->reset();
        $this->assertSame($user, $cursor->getNext());

        $cursor->reset();
        $this->assertSame($user, $cursor->getSingleResult());

        $cursor->reset();
        $this->assertSame(array($user), $cursor->toArray(false));
    }

    public function testRecreateShouldPreserveSorting()
    {
        $usernames = array('David', 'Xander', 'Alex', 'Kris', 'Jon');

        foreach ($usernames as $username){
            $user = new User();
            $user->setUsername($username);
            $this->dm->persist($user);
        }

        $this->dm->flush();

        $qb = $this->dm->createQueryBuilder('Documents\User')
            ->sort('username', 'asc');

        $cursor = $qb->getQuery()->execute();
        sort($usernames);

        foreach ($usernames as $username) {
            $this->assertEquals($username, $cursor->getNext()->getUsername());
        }

        $cursor->recreate();

        foreach ($usernames as $username) {
            $this->assertEquals($username, $cursor->getNext()->getUsername());
        }
    }

    public function testGetSingleResultPreservesLimit()
    {
        $usernames = array('David', 'Xander', 'Alex', 'Kris', 'Jon');

        foreach ($usernames as $username){
            $user = new User();
            $user->setUsername($username);
            $this->dm->persist($user);
        }

        $this->dm->flush();

        $cursor = $this->dm->createQueryBuilder('Documents\User')
            ->sort('username', 'asc')
            ->limit(2)
            ->getQuery()
            ->execute();

        $user = $cursor->getSingleResult();

        $users = $cursor->toArray();
        $this->assertCount(2, $users);
    }
}
